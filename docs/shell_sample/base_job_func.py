#!/usr/bin/python
# -*- coding: UTF-8 -*-

import glob
import os
import shutil
import time
import re
import datetime


def tail(f, n):
    stdin, stdout = os.popen2("tail -n " + str(n) + " " + f)
    stdin.close()
    lines = stdout.readlines()
    stdout.close()
    return lines


def explosion_func(file_patterns, left_tail_lines, keep_backup):
    print file_patterns, left_tail_lines, keep_backup

    print "Ready to explode for " + str(file_patterns.__len__()) + " file patterns"

    for file_pattern in file_patterns:
        print "Process File Pattern: ", file_pattern
        files = glob.glob(file_pattern)
        print "Found Files:", files

        for target_file in files:

            if not os.path.isfile(target_file):
                print "this is a directory, passover: ", target_file
                continue

            # (base_path, tail_of_path) = os.path.split(target_file)
            # print "base path: ", base_path
            # print "tail of path: ", tail_of_path

            print "Ready to explode file: ", target_file
            if keep_backup == 1:
                backup_file = target_file + "." + time.strftime("%Y%m%d-%H:%M", time.localtime()) + ".bak"
                shutil.copyfile(target_file, backup_file)
                print "Backup saved to ", backup_file

            tail_lines = []
            if left_tail_lines > 0:
                tail_lines = tail(target_file, left_tail_lines)
                print "Tail lines count: ", tail_lines.__len__()

            fo = open(target_file, "wb+")
            fo.writelines(tail_lines)
            fo.close()

            print "File Exploded: ", target_file

    return


# run as
# explosion_func(["/Users/Sinri/Codes/PycharmProjects/fstest/test/*.out"], 10, 1)


def antiquity_func(file_patterns, keep_days):
    print file_patterns, keep_days

    base_date = ((datetime.datetime.now() - datetime.timedelta(days=keep_days)).strftime("%Y-%m-%d"))
    print "Base date, ", (keep_days), " days ago: ", base_date
    print "Ready to remove antiquity for " + str(file_patterns.__len__()) + " file patterns"

    for file_pattern in file_patterns:
        print "Process File Pattern: ", file_pattern
        files = glob.glob(file_pattern)
        print "Found Files:", files

        for target_file in files:
            if not os.path.isfile(target_file):
                print "this is a directory, passover: ", target_file
                continue

            (base_path, tail_of_path) = os.path.split(target_file)
            # print "base path: ", base_path
            # print "tail of path: ", tail_of_path

            print "Ready to check antiquity file: ", tail_of_path, " in ", base_path

            found = re.search('(\d{4})-?(\d{2})-?(\d{2})', tail_of_path)
            if found:
                print "found-4: ", found.group()
                year = found.group(1)
            else:
                found = re.search('(\d{2})-?(\d{2})-?(\d{2})', tail_of_path)
                if found:
                    print "found-2: ", found.group()
                    year = "20" + found.group(1)
                else:
                    print "No date pattern matched, passover"
                    continue

            month = found.group(2)
            day = found.group(3)

            found_date = year + "-" + month + "-" + day
            print "Found date: ", found_date
            if found_date < base_date:
                os.remove(target_file)
                print target_file, "Earlier than base date, remove"

    return


# run as
# antiquity_func(["/Users/Sinri/Codes/PycharmProjects/fstest/test/*.bak"], 3)

def zombie_func(file_patterns, keep_days):
    print file_patterns, keep_days

    base_time = time.time() - keep_days * 3600 * 24
    print "base time is ", keep_days, " days ago i.e. ", base_time

    print "Ready to remove zombie for " + str(file_patterns.__len__()) + " file patterns"

    for file_pattern in file_patterns:
        print "Process File Pattern: ", file_pattern
        files = glob.glob(file_pattern)
        print "Found Files:", files

        for target_file in files:
            if not os.path.isfile(target_file):
                print "this is a directory, passover: ", target_file
                continue

            print "Ready to check zombie file: ", target_file
            fd = os.open(target_file, os.O_RDONLY)
            info = os.fstat(fd)
            print "st_atime: ", info.st_atime
            print "st_mtime: ", info.st_mtime
            print "st_ctime: ", info.st_ctime
            os.close(fd)

            if info.st_atime < base_time:
                os.remove(target_file)
                print target_file, "Last access time is earlier than the base time, remove"

    return

# run as
# zombie_func(["/Users/Sinri/Codes/PycharmProjects/fstest/test/*.bak",], 1)
